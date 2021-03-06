<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcomecontroller extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Crud_model');
        $this->load->library('form_validation');
        $this->load->helper('url_helper');
    }

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
        $dvd = new Crud_model();
        $dvd->setOptions('dvd', 'numD');
        $data['lastDvd'] = $dvd->getJoin(5, "numD", "dvd");
        $dvd = new Crud_model();
        $dvd->setOptions('notesmoyenne', 'dvdN');
        $data['moyennes'] = $dvd->getTopMoyenne();
        $this->load->template('welcome_message', $data);
	}

    public function abonnements() {
        $this->load->template('abonnements_view');
	}

    public function contact() {
        $this->load->template('contact_view');
    }

    public function magasins() {
        $magasins = new Crud_model();
        $magasins->setOptions('societe', 'numS');
        $data['magasins'] = $magasins->get();

        $this->load->template('magasins_view',$data);
    }

    public function genre($idgenre,$page=null)
    {
        $this->load->library('pagination');
        $dvds = new Crud_model();
        $dvds->setOptions('dvd', 'numD');
        if ($idgenre == "all") {
            $count = $dvds->get_total();
        } else {
            $count = $dvds->get_total(array('genre_numG'=>$idgenre));
        }

        $config['base_url'] = 'http://deeveadee.my/catalogue/genre/'.$idgenre.'/';
        $config['total_rows'] = $count;
        $config['use_page_numbers'] = TRUE;
        $config['num_links'] = '10';

        $config['next_link'] = 'Page suivante';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';

        $config['prev_link'] = 'Page précédente';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';

        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';

        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        $config['cur_tag_open'] = '<li class="active"><a href="">';
        $config['cur_tag_close'] = '</a></li>';
        $config['per_page'] = 100;
        $config['first_url'] = '/catalogue/genre/'.$idgenre.'/1';
        $config['full_tag_open'] = '<div id ="pagination" class="col-lg-12"><ul class="pagination">';
        $config['full_tag_close'] = '</ul></div>';
        $config['num_tag_open'] = '<li class="page">';
        $config['num_tag_close'] = '</li>';

        $deb = $config['per_page'] * $page - $config['per_page'];

        if ($idgenre != "all") {
            $data['dvds'] = $dvds->catalogue($config['per_page'],0,$idgenre);
        } else {
            $data['dvds'] = $dvds->catalogue($config['per_page'],$deb,"all");
        }

        $genres = new Crud_model();
        $genres->setOptions('genre', 'numG');
        $data['genres'] = $genres->get();
        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();
        $data['idgenre'] =  $idgenre;
        $data['deb'] =  $deb;

        $this->load->template('catalogue_view', $data);
//        $this->output->enable_profiler(TRUE);
    }

    public function detaildvd()
    {
        $id = $this->uri->segment(2);
        $dvds = new Crud_model();
        $dvds->setOptions('dvd', 'numD');
        $data['dvd'] = $dvds->getByJoin($id, "dvd","titreD,auteurD,anneeD,nomG,dateAchatD,nombreD,consultationsD,nomS,numD");

        $notes = new Crud_model();
        $notes->setOptions('notes', 'numN');
        $total = $notes->notes($id);
        if (count($total)>0) {
            $moyenne = new Crud_model();
            $moyenne->setOptions('notesmoyenne', 'dvdN');
            $data['moyenne'] = $moyenne->getTopMoyenne($id);
            $data['moyenne'] = intval(round($data['moyenne'][0]['moyenne']));
            if(isset($_SESSION['numC'])) {
                $data['anote'] = $notes->get_total(array('dvdN' => $id, 'clientN' => $_SESSION['numC'])); }
            $consult = $data['dvd'][0]['consultationsD'] + 1;
            $dvds->update($id, null, ['consultationsD' => $consult]);
        }

        $data['totalnotes'] = $notes->get_total(array('dvdN' => $id));
        $data['numc'] = $this->session->userdata('numC');
        $data['prenom'] = $this->session->userdata('prenom');

        $remarques = new Crud_model();
        $remarques->setOptions('remarques', 'numR');
        $data['listeremarques'] = $remarques->remarques($id);

        $this->load->library('table');
        $this->table->set_heading('Titre', 'Auteur', 'Année', 'Genre', 'Date d\'achat', 'Nombre(s) disponible(s)', 'Consultation(s)', 'Société');
        $template = array(
            'table_open'            => '<table border="0" class="col-md-12">'
        );
        $this->table->set_template($template);
        $data['tab'] = $this->table->generate($data['dvd']);
        if($this->session->userdata('isUserLoggedIn')){ $data['isUserLoggedIn'] = true; } else { $data['isUserLoggedIn'] = false; }

        $this->load->template('detaildvd_view',$data);
    }

    public function emprunt()
    {
        $dvd = new Crud_model();
        $dvd->setOptions('dvd','numD');
        $dispo = $dvd->get(1);
        if ( $dispo[0]['nombreD'] > 0) {
            $emprunt = new Crud_model();
            $emprunt->setOptions('emprunt','numE');
            $emprunt->insert(['dvdE' => $this->input->post('dvd'),'dureeE' => $this->input->post('duree'),'clientE' => $this->input->post('client'), 'retourE' => "NON", 'dateE' => date('Y-m-d')]);
            $reste = $dispo[0]['nombreD'] - 1;
            $id = $this->input->post('dvd');
            $dvd->update($id, null, ['nombreD' => $reste]);
            $data['info'] = "dvd reservé ok";
            $data['reste'] = $reste;
        } else {
            $data['error'] = "plus de dvd en stock";
        }

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($data));
    }

    public function note()
    {
        $notes = new Crud_model();
        $notes->setOptions('notes', 'numN');
        $data['n'] = $this->input->post('note');
        $data['i'] = $this->input->post('dvd');
        $data['c'] = $this->input->post('numC');
        $notes->insert(['dvdN' => $this->input->post('dvd'),'noteN' => $this->input->post('note'),'clientN' => $_SESSION['numC']]);
        $total = $notes->notes($this->input->post('dvd'));
            $data['moyenne'] = (array_sum(array_map(function ($arr) {
                    return $arr['noteN'];
                }, $total))) / count($total);
            $data['total'] = $total;
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($data));
    }

    public function remarque()
    {
        $data['test'] = "test";
        $data['dvd'] = $this->input->post('dvd');
        $data['remarque'] = $this->input->post('remarque');
        $remarque = new Crud_model();
        $remarque->setOptions('remarques', 'numR');
        $remarque->insert(['dvdR' => $this->input->post('dvd'),'commentairesR' => $this->input->post('remarque'),'clientR' => $this->input->post('client')]);

        $data['prenom'] = $this->session->userdata('prenom');

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($data));
    }

    public function checkSession()
    {
        if($this->session->userdata('isUserLoggedIn')){ $data = $_SESSION['isUserLoggedIn']; }
        else{
            $data = "ko";
        }
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($data));
    }

    public function test() {

    }
}
